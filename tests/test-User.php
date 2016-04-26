<?php
class UserTest extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
    }

    public function testDisplayUserFavorites() {

        global $current_user;

        $user_id = $current_user->ID;

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

        ob_start();
        geodir_add_to_favorite($post_id);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'Remove from Favorites', $output );


        $user_fav_posts = get_user_meta($user_id, 'gd_user_favourite_post', true);

        $this->assertContains( $post_id, $user_fav_posts );

        ob_start();
        geodir_user_show_favourites($user_id);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'geodir_my_favourites', $output );

    }

    public function testDisplayUserListings() {

        global $current_user;

        $user_id = $current_user->ID;

        ob_start();
        geodir_user_show_listings($user_id);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'geodir_my_listings', $output );

    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>